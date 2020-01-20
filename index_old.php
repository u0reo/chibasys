<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/core.php');
no_direct_access();
?>
        <div class="form-group">
          <label for="nendo">年度(*必須)</label>
          <select id="nendo" class="form-control stb">
            <option value="2020">2020</option>
            <option value="2019" selected>2019</option>
            <option value="2018">2018</option>
            <option value="2017">2017</option>
            <option value="2016">2016</option>
          </select>
        </div>
        <div class="form-group inline-parent">
          <label for="jikanwariShozokuCode">時間割所属</label>
          <select id="jikanwariShozokuCode" class="form-control">
            <option value="" selected>指定なし</option>
            <option value="G1">普遍教育</option>
            <option value="L1">文学部</option>
            <option value="E1">教育学部</option>
            <option value="A1">法経学部</option>
            <option value="B1">法政経学部</option>
            <option value="S1">理学部</option>
            <option value="S11">　数学・情報数理学科</option>
            <option value="S12">　物理学科</option>
            <option value="S13">　化学科</option>
            <option value="S14">　生物学科</option>
            <option value="S15">　地球科学科</option>
            <option value="S18">　先進科学プログラム</option>
            <option value="M1">医学部</option>
            <option value="M11">　医学科</option>
            <option value="P1">薬学部</option>
            <option value="P13">　薬学科</option>
            <option value="P14">　薬科学科</option>
            <option value="N1">看護学部</option>
            <option value="N11">　看護学科</option>
            <option value="T1">工学部</option>
            <option value="T1V">　総合工学科</option>
            <option value="T1V1">　　建築学コース</option>
            <option value="T1V2">　　都市環境システムコース</option>
            <option value="T1V3">　　デザインコース</option>
            <option value="T1V4">　　機械工学コース</option>
            <option value="T1V5">　　医工学コース</option>
            <option value="T1V6">　　電気電子工学コース</option>
            <option value="T1V7">　　物質科学コース</option>
            <option value="T1V8">　　共生応用化学コース</option>
            <option value="T1V9">　　情報工学コース</option>
            <option value="T1E">　都市環境システム学科</option>
            <option value="T1K">　先進科学プログラム</option>
            <option value="T1K2">　　工学部先進科学プログラム(フロンティア)</option>
            <option value="T1L">　メディカルシステム工学科</option>
            <option value="T1M">　共生応用化学科Aコース</option>
            <option value="T1N">　建築学科</option>
            <option value="T1P">　デザイン学科</option>
            <option value="T1Q">　機械工学科</option>
            <option value="T1R">　電気電子工学科</option>
            <option value="T1S">　ナノサイエンス学科</option>
            <option value="T1T">　画像科学科</option>
            <option value="T1U">　情報画像学科</option>
            <option value="T1F">　デザイン工学科Aコース</option>
            <option value="T1F4">　　建築コース</option>
            <option value="H1">園芸学部</option>
            <option value="Z1">国際教養学部</option>
            <option value="Z11">　国際教養学科</option>
            <option value="E2">教育学研究科</option>
            <option value="E21">　学校教育専攻</option>
            <option value="E215">　　学校心理学コース</option>
            <option value="E216">　　発達教育科学コース</option>
            <option value="E22">　国語教育専攻</option>
            <option value="E23">　社会科教育専攻</option>
            <option value="E24">　数学教育専攻</option>
            <option value="E25">　理科教育専攻</option>
            <option value="E26">　音楽教育専攻</option>
            <option value="E27">　美術教育専攻</option>
            <option value="E28">　保健体育専攻</option>
            <option value="E2A">　家政教育専攻</option>
            <option value="E2B">　英語教育専攻</option>
            <option value="E2C">　養護教育専攻</option>
            <option value="E2D">　学校教育臨床専攻</option>
            <option value="E2E">　カリキュラム開発専攻</option>
            <option value="E2F">　特別支援専攻</option>
            <option value="E2G">　スクールマネジメント専攻</option>
            <option value="E2H">　学校教育科学専攻</option>
            <option value="E2H1">　　教育発達支援系</option>
            <option value="E2H2">　　教育開発臨床系</option>
            <option value="E2I">　教科教育科学専攻</option>
            <option value="E2I1">　　言語・社会系</option>
            <option value="E2I2">　　理数・技術系</option>
            <option value="E2I3">　　芸術・体育系</option>
            <option value="S2">理学研究科</option>
            <option value="S21">　基盤理学専攻</option>
            <option value="S211">　　数学・情報数理学コース</option>
            <option value="S212">　　物理学コース</option>
            <option value="S213">　　化学コース</option>
            <option value="S22">　地球生命圏科学専攻</option>
            <option value="S221">　　生物学コース</option>
            <option value="S222">　　地球科学コース</option>
            <option value="S23">　基盤理学専攻</option>
            <option value="S231">　　数学・情報数理学コース</option>
            <option value="S232">　　物理学コース</option>
            <option value="S233">　　化学コース</option>
            <option value="S24">　地球生命圏科学専攻</option>
            <option value="S241">　　生物学コース</option>
            <option value="S242">　　地球科学コース</option>
            <option value="N2">看護学研究科</option>
            <option value="N21">　看護学専攻</option>
            <option value="N265">　　国際プログラム(訪問)</option>
            <option value="N266">　　国際プログラム(看護管理)</option>
            <option value="N267">　　国際プログラム(看護病態)</option>
            <option value="T2">工学研究科</option>
            <option value="T21">　建築・都市科学専攻</option>
            <option value="T211">　　建築学コース</option>
            <option value="T212">　　都市環境システムコース</option>
            <option value="T22">　デザイン科学専攻</option>
            <option value="T221">　　デザイン科学コース</option>
            <option value="T23">　人工システム科学専攻</option>
            <option value="T231">　　機械系コース</option>
            <option value="T232">　　電気電子系コース</option>
            <option value="T233">　　メディカルシステムコース</option>
            <option value="T24">　共生応用化学専攻</option>
            <option value="T241">　　共生応用化学コース</option>
            <option value="T25">　建築・都市科学専攻</option>
            <option value="T251">　　建築学コース</option>
            <option value="T252">　　都市環境システムコース</option>
            <option value="T26">　デザイン科学専攻</option>
            <option value="T261">　　デザイン科学コース</option>
            <option value="T27">　人工システム科学専攻</option>
            <option value="T271">　　機械系コース</option>
            <option value="T272">　　電気電子系コース</option>
            <option value="T273">　　メディカルシステムコース</option>
            <option value="T28">　共生応用化学専攻</option>
            <option value="T281">　　共生応用化学コース</option>
            <option value="H2">園芸学研究科</option>
            <option value="I2">人文社会科学研究科</option>
            <option value="I21">　地域文化形成専攻</option>
            <option value="I213">　　言語行動</option>
            <option value="I22">　公共研究専攻</option>
            <option value="I221">　　公共思想制度研究</option>
            <option value="I222">　　共生社会基盤研究</option>
            <option value="I23">　社会科学研究専攻</option>
            <option value="I232">　　経済理論・政策学(経</option>
            <option value="I233">　　経済理論・政策学(金</option>
            <option value="I24">　総合文化研究専攻</option>
            <option value="I241">　　言語構造</option>
            <option value="I243">　　人間行動</option>
            <option value="I25">　先端経営科学専攻</option>
            <option value="I26">　公共研究専攻</option>
            <option value="I261">　　公共哲学</option>
            <option value="I27">　社会科学研究専攻</option>
            <option value="I28">　文化科学研究専攻</option>
            <option value="I281">　　比較言語文化</option>
            <option value="Y2">融合科学研究科</option>
            <option value="Y21">　ナノサイエンス専攻</option>
            <option value="Y211">　　ナノ物性コース</option>
            <option value="Y212">　　ナノバイオロジーコー</option>
            <option value="Y22">　情報科学専攻</option>
            <option value="Y221">　　画像マテリアルコース</option>
            <option value="Y222">　　知能情報コース(前期</option>
            <option value="Y23">　ナノサイエンス専攻</option>
            <option value="Y231">　　ナノ物性コース(後期</option>
            <option value="Y232">　　ナノバイオロジーコー</option>
            <option value="Y24">　情報科学専攻</option>
            <option value="Y241">　　画像マテリアル 後期</option>
            <option value="Y242">　　知能情報コース</option>
            <option value="J2">医学薬学府</option>
            <option value="J21">　総合薬品科学専攻</option>
            <option value="J22">　医療薬学専攻</option>
            <option value="J23">　環境健康科学専攻</option>
            <option value="J231">　　医学領域</option>
            <option value="J232">　　薬学領域</option>
            <option value="J24">　先進医療科学専攻</option>
            <option value="J241">　　医学領域</option>
            <option value="J242">　　薬学領域</option>
            <option value="J25">　先端生命科学専攻</option>
            <option value="J251">　　医学領域</option>
            <option value="J252">　　薬学領域</option>
            <option value="J26">　創薬生命科学専攻</option>
            <option value="J27">　医科学専攻</option>
            <option value="J28">　先端医学薬学専攻</option>
            <option value="J281">　　先端生命(医学)</option>
            <option value="J282">　　先端生命(薬学)</option>
            <option value="J283">　　免疫統御(医学)</option>
            <option value="J284">　　免疫統御(薬学)</option>
            <option value="J285">　　先端臨床(医学)</option>
            <option value="J286">　　先端臨床(薬学)</option>
            <option value="J287">　　がん先端(医学)</option>
            <option value="J288">　　がん先端(薬学)</option>
            <option value="J29">　先端創薬科学専攻</option>
            <option value="J2A">　先進予防医学共同専攻</option>
            <option value="K2">専門法務研究科</option>
            <option value="W2">融合理工学府</option>
            <option value="W20">　数学情報科学専攻</option>
            <option value="W201">　　数学・情報数理学コース</option>
            <option value="W202">　　情報科学コース</option>
            <option value="W21">　地球環境科学専攻</option>
            <option value="W211">　　地球科学コース</option>
            <option value="W212">　　リモートセンシングコース</option>
            <option value="W213">　　都市環境システムコース</option>
            <option value="W22">　先進理化学専攻</option>
            <option value="W221">　　物理学コース</option>
            <option value="W222">　　物質科学コース</option>
            <option value="W223">　　化学コース</option>
            <option value="W224">　　共生応用化学コース</option>
            <option value="W225">　　生物学コース</option>
            <option value="W23">　創成工学専攻</option>
            <option value="W231">　　建築学コース</option>
            <option value="W232">　　イメージング科学コース</option>
            <option value="W233">　　デザインコース</option>
            <option value="W24">　基幹工学専攻</option>
            <option value="W241">　　機械工学コース</option>
            <option value="W242">　　医工学コース</option>
            <option value="W243">　　電気電子工学コース</option>
            <option value="W25">　数学情報科学専攻</option>
            <option value="W251">　　数学・情報数理学コース</option>
            <option value="W252">　　情報科学コース</option>
            <option value="W26">　地球環境科学専攻</option>
            <option value="W261">　　地球科学コース</option>
            <option value="W262">　　リモートセンシングコース</option>
            <option value="W263">　　都市環境システムコース</option>
            <option value="W27">　先進理化学専攻</option>
            <option value="W271">　　物理学コース</option>
            <option value="W272">　　物質科学コース</option>
            <option value="W273">　　化学コース</option>
            <option value="W274">　　共生応用化学コース</option>
            <option value="W275">　　生物学コース</option>
            <option value="W28">　創成工学専攻</option>
            <option value="W281">　　建築学コース</option>
            <option value="W282">　　イメージング科学コース</option>
            <option value="W283">　　デザインコース</option>
            <option value="W29">　基幹工学専攻</option>
            <option value="W291">　　機械工学コース</option>
            <option value="W292">　　医工学コース</option>
            <option value="W293">　　電気電子工学コース</option>
            <option value="D2">人文公共学府</option>
            <option value="D21">　人文科学専攻</option>
            <option value="D22">　公共社会科学専攻</option>
            <option value="D23">　人文公共学専攻</option>
            <option value="H3">園芸学部園芸別科</option>
            <option value="C1">留学生</option>
            <option value="G2">大学院共通教育</option>
          </select>
        </div>
        <div class="form-group">
          <label for="gakkiKubunCode">学期</label>
          <select id="gakkiKubunCode" class="form-control stb">
            <option value="" selected="selected">なし</option>
            <option value="1">前期</option>
            <option value="2">後期</option>
          </select>
        </div>
        <div class="form-group">
          <label for="kaikoKubunCode">ターム(一部の選択肢省略)</label>
          <select id="kaikoKubunCode" class="form-control stb">
            <option value="" selected="selected">なし</option>
            <option value="1">前期</option>
            <option value="2">後期</option>
            <option value="3">通年</option>
            <option value="4">集中</option>
            <option value="5">年度跨り</option>
            <option value="6">T1</option>
            <option value="7">T2</option>
            <option value="8">T3</option>
            <option value="9">T4</option>
            <option value="A">T5</option>
            <option value="B">T6</option>
            <option value="C">T1-2</option>
            <option value="D">T4-5</option>
            <option value="E">前期集中</option>
            <option value="F">後期集中</option>
            <!--<option value="G">1・4T</option>
              <option value="H">1・5T</option>
              <option value="I">2・4T</option>
              <option value="J">2・5T</option>
              <option value="K">1-3T</option>
              <option value="L">2-3T</option>
              <option value="M">2-4T</option>
              <option value="N">4-6T</option>
              <option value="O">5-6T</option>
              <option value="P">1T集中</option>
              <option value="Q">2T集中</option>
              <option value="R">3T集中</option>
              <option value="S">4T集中</option>
              <option value="T">5T集中</option>
              <option value="U">6T集中</option>
              <option value="V">1-2T集中</option>
              <option value="W">4-5T集中</option>
              <option value="X">1-3T集中</option>
              <option value="Y">2-3T集中</option>
              <option value="Z">2-4T集中</option>
              <option value="a">4-6T集中</option>
              <option value="b">5-6T集中</option>-->
          </select>
        </div>
        <div class="form-group md-form inline-parent">
          <label for="kyokannmLike">教員名</label>
          <input type="text" id="kyokannmLike" class="form-control">
        </div>
        <div class="form-group md-form inline-parent">
          <label for="jikanwaricdLike">授業コード</label>
          <input type="text" id="jikanwaricdLike" class="form-control">
        </div>
        <div class="form-group md-form inline-parent">
          <label for="kaikoKamokunmLike">授業科目名</label>
          <input type="text" id="kaikoKamokunmLike" class="form-control">
        </div>
        <div class="form-group">
          <label for="nenji">年次</label>
          <select id="nenji" class="form-control stb">
            <option value="" selected="selected">なし</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
          </select>
        </div>
        <div class="form-group">
          <label for="yobi">曜日</label>
          <select id="yobi" class="form-control stb">
            <option value="" selected="selected">なし</option>
            <option value="1">月</option>
            <option value="2">火</option>
            <option value="3">水</option>
            <option value="4">木</option>
            <option value="5">金</option>
            <option value="6">土</option>
            <!--<option value="9">その他</option>-->
          </select>
        </div>
        <div class="form-group">
          <label for="jigen">時限</label>
          <select id="jigen" class="form-control stb">
            <option value="" selected="selected">なし</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <!--<option value="0">その他</option>-->
          </select>
        </div>
        <div class="form-group md-form inline-parent" style="display:none;">
          <label for="freeWord">フリーワード</label>
          <input type="text" id="freeWord" class="form-control">
        </div>